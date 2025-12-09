INSERT IGNORE INTO companies (name) VALUES
('PT Alpha'),('PT Beta'),('PT Gamma');

INSERT IGNORE INTO warehouses (name) VALUES
('Gudang Jakarta'),('Gudang Surabaya');

INSERT IGNORE INTO destinations (name) VALUES
('Kalimantan'),('Sumatera'),('Sulawesi');

-- 10 PC items
INSERT IGNORE INTO items (sku, name, barcode, description) VALUES
('PC-001','PC 001','PC001','Unit PC 1'),
('PC-002','PC 002','PC002','Unit PC 2'),
('PC-003','PC 003','PC003','Unit PC 3'),
('PC-004','PC 004','PC004','Unit PC 4'),
('PC-005','PC 005','PC005','Unit PC 5'),
('PC-006','PC 006','PC006','Unit PC 6'),
('PC-007','PC 007','PC007','Unit PC 7'),
('PC-008','PC 008','PC008','Unit PC 8'),
('PC-009','PC 009','PC009','Unit PC 9'),
('PC-010','PC 010','PC010','Unit PC 10');
