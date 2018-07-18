#!/bin/bash
psql -c "DROP DATABASE test" -U postgres;
psql -c "CREATE DATABASE test" -U postgres;
psql -c "$(cat ./timestamp_behavior.sql)" -U postgres -d test;
