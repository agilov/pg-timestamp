-- Actual function that performs timestamp behavior
CREATE OR REPLACE FUNCTION timestamp_behavior_func()
  RETURNS TRIGGER AS $$
DECLARE
  _column TEXT := TG_ARGV [0];
  _mode   TEXT := TG_ARGV [1];
  _json   TEXT;
BEGIN

  IF (_mode = 'epoch') THEN
    _json = '{"' || _column || '":' || (extract(EPOCH FROM now()) :: INTEGER) || '}';
  ELSIF (_mode = 'float') THEN
      _json = '{"' || _column || '":' || (extract(EPOCH FROM now()) :: FLOAT) || '}';
  ELSE
    _json = '{"' || _column || '":"' || (now() :: TEXT) || '"}';
  END IF;

  NEW := json_populate_record(NEW, _json :: JSON);

  RETURN NEW;
END
$$ LANGUAGE plpgsql;

-- Creates trigger to fill specific columns of table on INSERT or UPDATE events
CREATE OR REPLACE FUNCTION attach_timestamp_behavior(
  _table  REGCLASS,
  _column TEXT,
  _event  TEXT DEFAULT 'INSERT',
  _mode   TEXT DEFAULT 'timestamp'
)
  RETURNS VOID AS $$
BEGIN
  -- Drop existing triggers if they exist.
  EXECUTE detach_timestamp_behavior(_table, _column);

  EXECUTE 'CREATE TRIGGER timestamp_behavior_trigger_' || _column || ' BEFORE ' || _event || ' ON ' || _table || ' ' ||
          'FOR EACH ROW ' ||
          'EXECUTE PROCEDURE timestamp_behavior_func(' || quote_literal(_column) || ', ' || quote_literal(_mode) ||
          ');';

END;
$$ LANGUAGE plpgsql;

-- Detach behavior
CREATE OR REPLACE FUNCTION detach_timestamp_behavior(_table REGCLASS, _column TEXT)
  RETURNS VOID AS $$
BEGIN
  EXECUTE 'DROP TRIGGER IF EXISTS timestamp_behavior_trigger_' || _column || ' ON ' || _table;
END;
$$ LANGUAGE plpgsql;
