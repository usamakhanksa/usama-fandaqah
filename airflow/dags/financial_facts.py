from airflow import DAG
from airflow.operators.python import PythonOperator
from datetime import datetime, timedelta

def process_frozen_transactions():
    """
    1. Fetch transactions where is_frozen = 1 and dw_synced_at is NULL
    2. Map to dim_user, dim_hotel
    3. Insert into fact_service_revenue (if service) or fact_deposits (if payment)
    4. Bulk update production MySQL dw_synced_at
    """
    pass

default_args = {
    'owner': 'fandaqah_dw',
    'start_date': datetime(2026, 4, 1),
}

with DAG('frozen_transactions', default_args=default_args, schedule_interval='@hourly', catchup=False) as dag:
    PythonOperator(task_id='process_frozen', python_callable=process_frozen_transactions)

def process_service_revenue():
    """
    1. Aggregates service charges by category and hotel
    2. Uses dw_service_category_classification for mapping
    3. Writes to fact_service_revenue
    """
    pass

with DAG('service_revenue', default_args=default_args, schedule_interval='@daily', catchup=True) as dag_rev:
    PythonOperator(task_id='process_services', python_callable=process_service_revenue)
