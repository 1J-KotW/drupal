import sqlite3

# Create database
conn = sqlite3.connect('workshop_bot.db')
cursor = conn.cursor()

# Create tables
cursor.execute('''
CREATE TABLE order_items (
    internal_id TEXT,
    title TEXT,
    code TEXT
)
''')

cursor.execute('''
CREATE TABLE assigned_tasks (
    order_internal_id TEXT,
    task_code TEXT,
    applied_norm_hours REAL
)
''')

cursor.execute('''
CREATE TABLE work_sessions (
    task_code TEXT,
    start_time TEXT,
    end_time TEXT
)
''')

# Insert sample data
cursor.execute("INSERT INTO order_items VALUES ('ORD001', 'Product A', 'PA001')")
cursor.execute("INSERT INTO order_items VALUES ('ORD002', 'Product B', 'PB001')")

cursor.execute("INSERT INTO assigned_tasks VALUES ('ORD001', 'TASK001', 10.0)")
cursor.execute("INSERT INTO assigned_tasks VALUES ('ORD001', 'TASK002', 5.0)")
cursor.execute("INSERT INTO assigned_tasks VALUES ('ORD002', 'TASK003', 8.0)")

# Sessions for TASK001: 2 hours
cursor.execute("INSERT INTO work_sessions VALUES ('TASK001', '2023-01-01 08:00:00', '2023-01-01 10:00:00')")
# For TASK002: 3 hours
cursor.execute("INSERT INTO work_sessions VALUES ('TASK002', '2023-01-01 10:00:00', '2023-01-01 13:00:00')")
# For TASK003: 4 hours
cursor.execute("INSERT INTO work_sessions VALUES ('TASK003', '2023-01-01 14:00:00', '2023-01-01 18:00:00')")

# One active session (end_time NULL)
cursor.execute("INSERT INTO work_sessions VALUES ('TASK001', '2023-01-01 15:00:00', NULL)")

conn.commit()
conn.close()

print("Test database created with sample data.")
