# Adviverse Ads (WordPress)

Single-file plugin. Copy `adviverse/` to `wp-content/plugins/` and activate.

- Shortcode: `[adviverse_ad tag="tk_..." sizes="300x250"]` (`sizes` optional: `WxH` or `fluid`)
- Block: "Adviverse Ad" (`adviverse/ad`, server-rendered; the editor shows it via the block's attributes, no custom editor UI yet)
- The SDK `https://serve.adviverse.com/sdk/v1.js` is enqueued async in the footer only when a slot renders.
- Output is `<div><script data-tag=".." data-sizes=".."></script></div>`, the marker the SDK fills.
