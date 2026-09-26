<?php
/** Standalone error page. Deliberately does not load config or the database,
 *  so it can never redirect and can never take part in a loop. */
http_response_code(isset($_GET['c']) && $_GET['c'] === '403' ? 403 : 404);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page not found</title>
<style>
  body{font:400 16px/1.65 -apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#22302B;
       background:#F7F6F1;margin:0;padding:16vh 20px;text-align:center}
  h1{font:400 30px/1.2 Georgia,serif;margin:0 0 10px}
  p{color:#6B7671;margin:0 0 24px}
  a{display:inline-block;padding:11px 22px;border-radius:8px;background:#12352B;
    color:#fff;text-decoration:none;font-size:15px}
</style></head><body>
  <h1>Page not found</h1>
  <p>That address does not exist here.</p>
  <a href="/">Back to the homepage</a>
</body></html>
