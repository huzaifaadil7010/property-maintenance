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

To report a maintenance issue in Claude Desktop, ask Claude to open `open-resident-issue-image-picker`. Choose 1–10 JPEG, PNG, or WebP images in the picker (up to 10 MB each), review the request summary and image list, then click **Approve and create request** within five minutes. The picker uploads files automatically to short-lived resident-owned storage; confirmation moves them to the request's permanent `issue-images` collection. You do not need to create a server folder or pass file paths. Images already attached to a Claude message are not automatically transferred to the MCP tool; choose them in the picker. Abandoned MCP uploads expire after 30 minutes and are pruned by the scheduled `resident-mcp:prune-uploads` command; run Laravel's scheduler locally for timely cleanup.

The same preview and confirmation sequence applies to confirming a completed request and reopening one with a reason. Read tools are available without confirmation. Use `get-resident-maintenance-request` to find an attachment ID, then `get-resident-request-image` for a bounded visual preview.
