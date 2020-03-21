## Migrating your existing records

### From UUIDs as primary key
This is the most work, especially if you have relations (UUID foreign keys) as you also need to resolve all those along with it.

Here you first need to migrate to an integer primary key, and then afterwards follow the "Quick Start for adding to existing records"
and let the provided commands guide you to the rest.

#### UUID to AIID primary key
Step 1: Add a the new primary key column `id_new`, but as a normal `int(10)` field with auto-increment for now.
Make sure the column is filled with values now.

Step 2: Now adjust all foreign key values to the new primary key value, with some console command you can run over all records.

Step 3: Now rename the old `id` to `id_old` and the `id_new` to `id`. Switch the primary key constraint over to this one.
Do so also for all other composite indexes and constraints.

If you want to use the existing UUIDs, you can also rename it directly to `uuid` instead of `id_old`.
Then Expose can directly use it. This is especially important if you want to keep existing links intact (SEO or otherwise).

Make sure your data validation in the models (Table classes usually) don't check for the primary key here too strictly (string vs int)
and also the rest of the code is agnostic enough now to handle integer IDs.
