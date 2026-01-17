import datetime

# Simulate the calculations

def calculate_fact(sessions):
    fact = 0
    for session in sessions:
        if session['end_time']:
            start = datetime.datetime.fromisoformat(session['start_time'])
            end = datetime.datetime.fromisoformat(session['end_time'])
            fact += (end - start).total_seconds() / 3600
    return fact

# Sample data
tasks_ord001 = [
    {'task_code': 'TASK001', 'applied_norm_hours': 10.0},
    {'task_code': 'TASK002', 'applied_norm_hours': 5.0},
]

sessions = {
    'TASK001': [
        {'start_time': '2023-01-01T08:00:00', 'end_time': '2023-01-01T10:00:00'},  # 2 hours
        {'start_time': '2023-01-01T15:00:00', 'end_time': None},  # active, ignore
    ],
    'TASK002': [
        {'start_time': '2023-01-01T10:00:00', 'end_time': '2023-01-01T13:00:00'},  # 3 hours
    ],
    'TASK003': [
        {'start_time': '2023-01-01T14:00:00', 'end_time': '2023-01-01T18:00:00'},  # 4 hours
    ],
}

print("Report for ORD001:")
for task in tasks_ord001:
    task_code = task['task_code']
    plan = task['applied_norm_hours']
    fact = calculate_fact(sessions.get(task_code, []))
    delta = plan - fact
    print(f"Task: {task_code}, Plan: {plan}, Fact: {fact}, Delta: {delta}")

tasks_ord002 = [
    {'task_code': 'TASK003', 'applied_norm_hours': 8.0},
]

print("\nReport for ORD002:")
for task in tasks_ord002:
    task_code = task['task_code']
    plan = task['applied_norm_hours']
    fact = calculate_fact(sessions.get(task_code, []))
    delta = plan - fact
    print(f"Task: {task_code}, Plan: {plan}, Fact: {fact}, Delta: {delta}")
