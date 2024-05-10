## HitCounters2

This is an alternative approach to the concept of [HitCounters](https://www.mediawiki.org/wiki/Extension:HitCounters)

Instead of using deferred updates, it embeds some JavaScript code using to every page that calls a REST endpoint provided by this extension. This approach has several advantages over the original HitCounters:

 - Works with caching HTTP proxies.
 - Potentially more scalable. You could even have a dedicated MediaWiki server just to process HitCounters2 updates if you configure your reverse proxy for it.

### License

Copyright (C) 2024  Alex

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.