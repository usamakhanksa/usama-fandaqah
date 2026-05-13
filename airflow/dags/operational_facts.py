from airflow import DAG
from airflow.operators.python import PythonOperator
from datetime import datetime, timedelta

def run_ar_aging():
    """
    1. Fetch all active Promissories and InvoiceTransfers
    2. Calculate aging buckets (0-30, 31-60, etc.)
    3. Truncate/Insert into fact_ar_aging (snapshot based)
    """
    pass

def sync_commissions():
    """
    1. Fetch approved commissions
    2. Write to fact_commissions
    """
    pass

def sync_cashier_shifts():
    """
    1. Fetch closed shifts
    2. Write to fact_cashier_shifts
    """
    pass

default_args = {
    'owner': 'fandaqah_dw',
    'start_date': datetime(2026, 4, 1),
}

with DAG('promissory_aging', default_args=default_args, schedule_interval='0 2 * * *') as dag_aging:
    PythonOperator(task_id='ar_aging', python_callable=run_ar_aging)

with DAG('additional_facts', default_args=default_args, schedule_interval='@daily') as dag_extra:
    t1 = PythonOperator(task_id='commissions', python_callable=sync_commissions)
    t2 = PythonOperator(task_id='cashier_shifts', python_callable=sync_cashier_shifts)
    t1 >> t2
