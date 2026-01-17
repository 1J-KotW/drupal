import sqlite3
import json
import datetime

# Simulate the full flow with logging

def log(message, level='info'):
    timestamp = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    print(f"[{timestamp}] {level.upper()}: {message}")

def calculate_fact(sessions):
    fact = 0
    for session in sessions:
        if session['end_time']:
            start = datetime.datetime.fromisoformat(session['start_time'])
            end = datetime.datetime.fromisoformat(session['end_time'])
            fact += (end - start).total_seconds() / 3600
    return fact

# Connect to SQLite
conn = sqlite3.connect('workshop_bot.db')
cursor = conn.cursor()

log("Cron triggered: Starting data import from SQLite")

# Get order items
cursor.execute("SELECT internal_id, title, code FROM order_items")
orders = cursor.fetchall()
log(f"SQLiteReader: Retrieved {len(orders)} order items from SQLite")
for order in orders:
    log(f"Order item: {order[0]} - {order[1]}", 'debug')

for order in orders:
    internal_id, title, code = order
    log(f"Processing order {internal_id}")

    # Get assigned tasks
    cursor.execute("SELECT task_code, applied_norm_hours FROM assigned_tasks WHERE order_internal_id = ?", (internal_id,))
    tasks = cursor.fetchall()

    report_data = []
    for task in tasks:
        task_code, plan = task
        plan = float(plan)

        # Get work sessions
        cursor.execute("SELECT start_time, end_time FROM work_sessions WHERE task_code = ? AND end_time IS NOT NULL", (task_code,))
        sessions = cursor.fetchall()

        fact = calculate_fact([{'start_time': s[0], 'end_time': s[1]} for s in sessions])
        delta = plan - fact

        report_data.append({
            'task_code': task_code,
            'plan': plan,
            'fact': fact,
            'delta': delta
        })

    log(f"Calculated report for {internal_id}: {json.dumps(report_data)}", 'debug')

    # Simulate saving to Drupal node
    log(f"Saved report node for {internal_id} with data: {json.dumps(report_data)}")

log("Cron completed: Data import and calculation finished")

# Simulate page request
internal_id = 'ORD001'
log(f"Page request: Generating report for {internal_id}")

# Recalculate for page (in real Drupal, it would use cached data)
cursor.execute("SELECT task_code, applied_norm_hours FROM assigned_tasks WHERE order_internal_id = ?", (internal_id,))
tasks = cursor.fetchall()

report_data = []
for task in tasks:
    task_code, plan = task
    plan = float(plan)

    cursor.execute("SELECT start_time, end_time FROM work_sessions WHERE task_code = ? AND end_time IS NOT NULL", (task_code,))
    sessions = cursor.fetchall()

    fact = calculate_fact([{'start_time': s[0], 'end_time': s[1]} for s in sessions])
    delta = plan - fact

    report_data.append({
        'task_code': task_code,
        'plan': plan,
        'fact': fact,
        'delta': delta
    })

log(f"ReportController: Report data for {internal_id}: {json.dumps(report_data)}", 'debug')

# Simulate template rendering
print("\nRendered Report Page:")
print(f"<h1>Factory Report for {internal_id}</h1>")
print("<table><thead><tr><th>Код операции</th><th>НЧ План</th><th>Ч Факт</th><th>Дельта</th></tr></thead><tbody>")
for item in report_data:
    print(f"<tr><td>{item['task_code']}</td><td>{item['plan']}</td><td>{item['fact']}</td><td>{item['delta']}</td></tr>")
print("</tbody></table>")

conn.close()
