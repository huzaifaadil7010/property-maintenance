<x-mcp::app title="Report a maintenance issue">
    <x-slot:head>
        <style>
            body { font-family: system-ui, sans-serif; color: var(--color-text-primary, #18202a); background: var(--color-background-primary, #fff); margin: 0; padding: 20px; }
            main { max-width: 620px; margin: auto; }
            h1 { font-size: 1.35rem; margin: 0 0 16px; }
            label { display: block; font-weight: 600; margin-top: 14px; }
            input, select, textarea { box-sizing: border-box; width: 100%; margin-top: 5px; padding: 10px; border: 1px solid var(--color-border-primary, #b9c0c9); border-radius: 7px; background: var(--color-background-primary, #fff); color: inherit; font: inherit; }
            textarea { min-height: 100px; resize: vertical; }
            input[type=file] { padding: 8px; }
            button { margin-top: 16px; padding: 10px 16px; border: 0; border-radius: 7px; background: #2563eb; color: #fff; font: inherit; cursor: pointer; }
            button:disabled { opacity: .5; cursor: wait; }
            button.secondary { background: #526071; margin-left: 8px; }
            #status { min-height: 24px; margin-top: 14px; }
            #preview { margin-top: 18px; padding: 16px; border: 1px solid var(--color-border-primary, #b9c0c9); border-radius: 8px; }
            #preview[hidden] { display: none; }
            #preview p { margin: 7px 0; }
            #image-list { margin: 8px 0; padding-left: 22px; }
            .error { color: #b42318; }
        </style>
        <script type="module">
            createMcpApp(async (app) => {
                const form = document.getElementById('request-form');
                const filesInput = document.getElementById('images');
                const preview = document.getElementById('preview');
                const status = document.getElementById('status');
                const previewButton = document.getElementById('preview-button');
                const approveButton = document.getElementById('approve-button');
                const cancelButton = document.getElementById('cancel-button');
                const imageList = document.getElementById('image-list');
                const options = JSON.parse(document.querySelector('main').dataset.options);
                let staged = [];
                let token = null;
                let busy = false;

                const resultData = (result) => {
                    if (result.isError) throw new Error(result.content?.[0]?.text ?? 'Tool call failed.');
                    return result.structuredContent ?? JSON.parse(result.content?.[0]?.text ?? '{}');
                };

                const catalogCall = async (name, args) => {
                    const response = resultData(await app.callServerTool('execute_tools', {
                        calls: [{ name, arguments: args }],
                    }));
                    const entry = response.results?.[0];
                    if (!response.ok || !entry || entry.isError) {
                        throw new Error(entry?.content?.[0]?.text ?? response.error?.message ?? 'Tool call failed.');
                    }
                    return entry.structuredContent ?? JSON.parse(entry.content?.[0]?.text ?? '{}');
                };

                const setBusy = (value) => {
                    busy = value;
                    previewButton.disabled = value || Boolean(token);
                    approveButton.disabled = value;
                    cancelButton.disabled = value;
                    for (const element of form.elements) element.disabled = value || Boolean(token);
                };

                const setStatus = (message, error = false) => {
                    status.textContent = message;
                    status.classList.toggle('error', error);
                };

                const setOptions = (element, options, selected) => {
                    element.replaceChildren();
                    for (const option of options) {
                        const node = document.createElement('option');
                        node.value = option.value;
                        node.textContent = option.label;
                        element.append(node);
                    }
                    if (selected && options.some((option) => option.value === selected)) element.value = selected;
                };

                setOptions(form.elements.category, options.maintenance_categories);
                setOptions(form.elements.priority, options.maintenance_priorities);

                app.onToolInput((params) => {
                    const fields = params.arguments ?? {};
                    for (const name of ['title', 'description']) {
                        if (typeof fields[name] === 'string') form.elements[name].value = fields[name];
                    }
                    form.dataset.category = fields.category ?? '';
                    form.dataset.priority = fields.priority ?? '';
                    if (options.maintenance_categories.some((option) => option.value === fields.category)) form.elements.category.value = fields.category;
                    if (options.maintenance_priorities.some((option) => option.value === fields.priority)) form.elements.priority.value = fields.priority;
                });

                app.onToolResult((params) => {
                    try {
                        const data = resultData(params);
                        const fields = data.fields ?? {};
                        for (const name of ['title', 'description']) {
                            if (!form.elements[name].value && typeof fields[name] === 'string') form.elements[name].value = fields[name];
                        }
                        setOptions(form.elements.category, data.options.maintenance_categories, form.dataset.category || fields.category);
                        setOptions(form.elements.priority, data.options.maintenance_priorities, form.dataset.priority || fields.priority);
                    } catch (error) {
                        setStatus(error.message, true);
                    }
                });

                const sha256 = async (file) => {
                    const digest = await crypto.subtle.digest('SHA-256', await file.arrayBuffer());
                    return Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('');
                };

                const base64 = async (blob) => {
                    const bytes = new Uint8Array(await blob.arrayBuffer());
                    let binary = '';
                    for (let offset = 0; offset < bytes.length; offset += 8192) {
                        binary += String.fromCharCode(...bytes.subarray(offset, offset + 8192));
                    }
                    return btoa(binary);
                };

                const upload = async (file, number, count) => {
                    const chunkSize = 512 * 1024;
                    const total = Math.ceil(file.size / chunkSize);
                    const hash = await sha256(file);
                    let uploadId;

                    for (let index = 0; index < total; index++) {
                        setStatus(`Uploading image ${number} of ${count}, chunk ${index + 1} of ${total}…`);
                        const args = {
                            name: file.name,
                            size: file.size,
                            sha256: hash,
                            total_chunks: total,
                            chunk_index: index,
                            data: await base64(file.slice(index * chunkSize, (index + 1) * chunkSize)),
                        };
                        if (uploadId) args.upload_id = uploadId;
                        const response = resultData(await app.callServerTool('upload-resident-issue-image-chunk', args));
                        if (response.image_id) return response;
                        uploadId = response.upload_id;
                    }
                    throw new Error('The image upload did not finish.');
                };

                const discard = async () => {
                    if (!staged.length) return;
                    const images = staged.map((image) => image.image_id);
                    staged = [];
                    await app.callServerTool('discard-resident-issue-images', { images });
                };

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (busy) return;
                    const files = Array.from(filesInput.files ?? []);
                    if (files.length < 1 || files.length > 10) {
                        setStatus('Choose between one and ten images.', true);
                        return;
                    }
                    if (files.some((file) => file.size < 1 || file.size > 10 * 1024 * 1024)) {
                        setStatus('Each image must be at most 10 MB.', true);
                        return;
                    }

                    setBusy(true);
                    preview.hidden = true;
                    token = null;
                    try {
                        await discard();
                        for (const [index, file] of files.entries()) {
                            staged.push(await upload(file, index + 1, files.length));
                        }
                        const data = await catalogCall('prepare-create-resident-maintenance-request', {
                            title: form.elements.title.value,
                            category: form.elements.category.value,
                            priority: form.elements.priority.value,
                            description: form.elements.description.value,
                            images: staged.map((image) => image.image_id),
                        });
                        token = data.confirmation_token;
                        document.getElementById('preview-summary').textContent = data.summary;
                        document.getElementById('preview-details').textContent = form.elements.description.value;
                        document.getElementById('preview-expiry').textContent = `Approval expires at ${new Date(data.expires_at).toLocaleTimeString()}.`;
                        imageList.replaceChildren();
                        for (const image of data.impact.images) {
                            const item = document.createElement('li');
                            item.textContent = `${image.name} (${Math.ceil(image.size / 1024)} KiB)`;
                            imageList.append(item);
                        }
                        preview.hidden = false;
                        setStatus('Review the details and images, then approve to create the request.');
                    } catch (error) {
                        setStatus(error.message, true);
                    } finally {
                        setBusy(false);
                    }
                });

                approveButton.addEventListener('click', async () => {
                    if (!token || busy) return;
                    setBusy(true);
                    try {
                        const data = await catalogCall('confirm-resident-change', { confirmation_token: token });
                        token = null;
                        staged = [];
                        preview.hidden = true;
                        form.hidden = true;
                        setStatus(`Maintenance request #${data.result.id} created with ${filesInput.files.length} issue image(s).`);
                        try {
                            await app.sendMessage(`I approved and created resident maintenance request #${data.result.id} with issue images.`);
                        } catch {
                            // The request is already created; a chat notification failure must not imply otherwise.
                        }
                    } catch (error) {
                        token = null;
                        preview.hidden = true;
                        setStatus(`${error.message} Prepare a new preview before trying again.`, true);
                    } finally {
                        setBusy(false);
                    }
                });

                cancelButton.addEventListener('click', async () => {
                    if (busy) return;
                    setBusy(true);
                    try {
                        await discard();
                        token = null;
                        preview.hidden = true;
                        setStatus('Draft discarded. No maintenance request was created.');
                    } catch (error) {
                        setStatus(error.message, true);
                    } finally {
                        setBusy(false);
                    }
                });

                app.autoResize();
            });
        </script>
    </x-slot:head>

    <main data-options="{{ json_encode($options) }}">
        <h1>Report a maintenance issue</h1>
        <form id="request-form">
            <label for="title">Title</label>
            <input id="title" name="title" maxlength="255" required>
            <label for="category">Category</label>
            <select id="category" name="category" required></select>
            <label for="priority">Priority</label>
            <select id="priority" name="priority" required></select>
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
            <label for="images">Issue images (1–10, JPEG/PNG/WebP, up to 10 MB each)</label>
            <input id="images" name="images" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
            <button id="preview-button" type="submit">Upload and preview</button>
        </form>
        <p id="status" role="status" aria-live="polite"></p>
        <section id="preview" hidden>
            <h2>Confirm this request</h2>
            <p id="preview-summary"></p>
            <p id="preview-details"></p>
            <ul id="image-list"></ul>
            <p id="preview-expiry"></p>
            <button id="approve-button" type="button">Approve and create request</button>
            <button id="cancel-button" class="secondary" type="button">Discard images</button>
        </section>
    </main>
</x-mcp::app>
