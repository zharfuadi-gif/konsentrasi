-- Migration: Add keterlambatan column to absensi_231051 table
-- Run this SQL query in your phpMyAdmin or MySQL client

ALTER TABLE absensi_231051 
ADD COLUMN keterlambatan_menit_231051 INT DEFAULT 0 
COMMENT 'Jumlah menit keterlambatan (0 jika tidak terlambat)';
