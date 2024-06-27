-- gas consumption
TRUNCATE TABLE envirosense.gas_daily;
INSERT INTO envirosense.gas_daily (ts, value, total)
SELECT date AS ts, tick AS value, total AS total FROM sfinternetofthings.gas_daily_consumption WHERE date >= '2022-01-01';
INSERT INTO envirosense.gas_daily (ts, value)
SELECT date AS ts, gas as value FROM sfinternetofthings.monthly_consumption WHERE date <= '2021-12-31';

TRUNCATE TABLE envirosense.gas_hourly;
INSERT INTO envirosense.gas_hourly (ts, value)
SELECT datetime AS ts, tick AS value FROM sfinternetofthings.gas_hourly_consumption;

-- power consumption
TRUNCATE TABLE envirosense.power_daily;
INSERT INTO envirosense.power_daily (ts, value, scaler, total)
SELECT date AS ts, value AS value, scaler AS scaler, total AS total FROM sfinternetofthings.power_daily_consumption WHERE date >= '2022-01-01';
INSERT INTO envirosense.power_daily (ts, value, scaler)
SELECT date AS ts, power * 100 as value, -1 as scaler FROM sfinternetofthings.monthly_consumption WHERE date <= '2021-12-31';

TRUNCATE TABLE envirosense.power_hourly;
INSERT INTO envirosense.power_hourly (ts, value, scaler)
SELECT ts AS ts, value AS value, scaler AS scaler FROM sfinternetofthings.power_consumption;

-- sensors
TRUNCATE TABLE envirosense.sensor;
INSERT INTO envirosense.sensor (id, ts, mac, payload)
SELECT id AS id, ts AS ts, mac AS mac, payload AS payload FROM sfinternetofthings.sensor;

TRUNCATE TABLE envirosense.sensor_description;
INSERT INTO envirosense.sensor_description (id, mac, name, description, color)
SELECT id, mac, name, description, color FROM sfinternetofthings.sensor_description;

-- solar yield
TRUNCATE TABLE envirosense.solar_daily;
INSERT INTO envirosense.solar_daily (ts, value)
SELECT date AS ts, value AS value FROM sfinternetofthings.solar_daily;

TRUNCATE TABLE envirosense.solar_hourly;
INSERT INTO envirosense.solar_hourly (ts, value)
SELECT DATETIME AS ts, value as value FROM sfinternetofthings.solar_hourly;

-- heating system
TRUNCATE TABLE envirosense.heating_system;
INSERT INTO envirosense.heating_system (name, ts, value)
SELECT name AS name, ts AS ts, value AS value FROM sfinternetofthings.heater_value WHERE ts > DATE_SUB(NOW(), INTERVAL 14 DAY);

-- photovoltaics yield
TRUNCATE TABLE envirosense.photovoltaics_daily;
INSERT INTO envirosense.photovoltaics_daily (ts, total)
SELECT date AS ts, total AS total FROM sfinternetofthings.photovoltaic_daily;

TRUNCATE TABLE envirosense.photovoltaics_hourly;
INSERT INTO envirosense.photovoltaics_hourly (ts, value)
SELECT ts AS ts, value AS value FROM sfinternetofthings.photovoltaic_hourly;



-- overall

-- calculates the daily gain because we get only the total value from the shelly
UPDATE envirosense.photovoltaics_daily AS dest,
(
    SELECT ts, total, LAG(total,1) OVER (ORDER BY ts) prev_total,
	(total - 	lag(total,1) OVER (ORDER BY ts)) As diff
    FROM envirosense.photovoltaics_daily
) AS src
SET dest.value = src.diff
WHERE dest.ts = src.ts;