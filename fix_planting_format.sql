-- Fix planting_format enum to match controller validation
-- Run this SQL query in your database

-- First, update existing data
UPDATE planting_locations SET planting_format = 'ditanam_dalam_petak' WHERE planting_format = 'petak';
UPDATE planting_locations SET planting_format = 'row_crop' WHERE planting_format = 'row';

-- Then, update the enum definition
ALTER TABLE planting_locations 
MODIFY COLUMN planting_format ENUM('ditanam_dalam_petak', 'cover_crop', 'row_crop', 'lainnya') 
DEFAULT 'ditanam_dalam_petak';

