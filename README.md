# Features
* ReCaptcha Support
* Message Rate Limiting

# Installation
1. Copy ```config.php.dist``` to ```config.php```
2. Give write and execute permissions to the web server on the ```/cache```
   directory
3. Put your information into ```config.php```, especially fill in
   ```$recaptcha_public_key```, ```$recaptcha_private_key```
   and the recipient lists

**It is ESSENTIAL that you set ```$config['debug'] = false```, otherwise the
software may leak recipient mail addresses.**

# Documentation
## Rate Limiting
To implement hour-based Rate Limiting, the software keeps a cache file for
the current hour in the ```cache``` directory. For every message sent, one
byte is appended to this file.

When the file size becomes larger than the ```$config['ratelimit']```
configuration option, no messages can be sent until the next hour.