-- Migration: Add 'Sakit' to status column in absensi_231051 table
-- This will modify the status column to include 'Sakit' option

-- First, check current status column type
-- DESCRIBE absensi_231051;

-- Modify the status column to include 'Sakit' in the ENUM
ALTER TABLE absensi_231051 MODIFY COLUMN status_231051 ENUM('Hadir', 'Terlambat', 'Alfa', 'Izin', 'Sakit') DEFAULT 'Alfa';

-- Also add a note column for additional information about sick status
ALTER TABLE absensi_231051 ADD COLUMN catatan_231051 TEXT DEFAULT NULL COMMENT 'Catatan tambahan untuk status khusus seperti sakit/izin';