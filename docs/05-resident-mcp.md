# Local resident MCP server

The resident server is available only when Laravel runs in the `local` environment. It acts as `ethan.parker@northstar.test` in his current active organization and requires that organization to have a valid subscription. It cannot manage another resident's requests, account settings, or owner resources.

Add it to a local MCP client using the same PHP executable used by this project:

```json
{
  "mcpServers": {
    "resident": {
      "command": "/usr/bin/php",
      "args": [
        "/home/huzaifa/Desktop/workspace/property-maintenance/artisan",
        "mcp:start",
        "/mcp/resident"
      ]
    }
  }
}
```

Adjust the PHP path if `command -v php` reports a different executable. Restart or reconnect the client after changing its MCP configuration.

To report a maintenance issue, first create `storage/app/private/mcp/resident-inbox` and put 1–10 JPEG, PNG, or WebP files there. Each file must be at most 10 MB. Give `prepare-create-resident-maintenance-request` only the filenames, never paths. Review its summary and image list before calling `confirm-resident-change` with the one-use token within five minutes. The original inbox files are not removed automatically.

The same preview and confirmation sequence applies to confirming a completed request and reopening one with a reason. Read tools are available without confirmation. Use `get-resident-maintenance-request` to find an attachment ID, then `get-resident-request-image` for a bounded visual preview.
