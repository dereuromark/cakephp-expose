## Motivation

### Why UUIDs
This replaces [Hashid](https://github.com/dereuromark/cakephp-hashid) as a more explicit approach which has several advantages:
- Instead of cloaking the actual numeric primary key we use a dedicated secondary key, which removes side effects with less trivial use cases.
- The disk space addition of the UUID column is outweighed by the simple and robust usability.
- Joins and other DB specific operations now work flawlessly as they keep using the internal primary key relation.

One does have to make small adjustments to the public actions, though. All lookup by `id` need to be replaced with `uuid`.

It wouldn't even need to be a UUID, it could be any random key of any length. But UUIDs exist and are supported out of the box here.
So it is just super convenient to use them for this. And the framework supposed this out of the box across all layers.

### Why not Hash IDs (anymore)
Turns out that the auto(magic) overloading of the primary key (from int to string) is not really a solid approach.

Also:
- The cloaking is also not too secure (and can be reverse engineered), using a truly random UUID though solves this by design.
- The speed is not an issue actually if you use both AIID and UUID together. We only use the exposed field for the query conditions. All joins and internals continue to use
normal primary key relations. And we have an index on that exposed field, so it really doesn't slow things down much.

### Why AIID and UUID as combination
You might ask now: Why not only UUID as primary key?

This kills internal usability (hard to remember IDs and foreign keys), as well as usually increases DB size dramatically (as each foreign key is now also a UUID).
If you do not need this, you only make life harder this way.
The UUID in our case is only meant for external lookup. Everything inside the app should still be simple, easy and fast.

Further issues with only UUID as primary key:
- Loss of deterministic sorting (which you get for free keeping the AIID primary key) and pagination (especially with burst inputs around the same times).
- Queries are often much slower here still compared to integer AIID, especially when doing a lot of joins.

Issues with mixing - some primary keys as UUID and others as AIID:
- Easy to mess things up (e.g. some foreign_key now has to be varchar to allow both the int and the string)
- Less flexible in general of adding functionality on top. Keeping always primary key one type allows here for easier future additions.
