# PostgreSQL table timestamp behavior

[![Build Status](https://travis-ci.org/agilov/pg-timestamp.svg)](https://travis-ci.org/agilov/pg-timestamp)

PG function that provide timestamp behavior on table (setting created at or updated at timestamps before insert or update row).

Source SQL code in file ./timestamp_behavior.sql

## Usage examples

```sql
-- Create trigger that set now() to created_at_column in new row before insert
select attach_timestamp_behavior('test_table', 'created_at_column', 'INSERT');

-- Create trigger that set extract(epoch from now()) to created_at_column in new row before insert
select attach_timestamp_behavior('test_table', 'created_at_column', 'INSERT', 'epoch');

-- Create trigger that set extract(epoch from now())::float to created_at_column in new row before insert
select attach_timestamp_behavior('test_table', 'created_at_column', 'INSERT', 'float');

-- Create trigger that set now() to created_at_column in new row before insert or update
select attach_timestamp_behavior('test_table', 'updated_at_column', 'INSERT OR UPDATE', 'float');

-- Deleting timestamp behavior trigger for test_table and updated_at_column
select detach_timestamp_behavior('test_table', 'updated_at_column');
```

## Supported modes
- timestamp (will write now() value to field)
- epoch (will write integer epoch value to field)
- float (will write float epoch value to field)

Timestamp is default mode.

Any unknown modes considered as timestamp.
