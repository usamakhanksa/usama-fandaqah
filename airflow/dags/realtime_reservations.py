from airflow import DAG
from airflow.operators.python import PythonOperator
from datetime import datetime, timedelta
import logging

def sync_reservations(**kwargs):
    """
    1. Check etl_watermark for 'reservations' table
    2. Fetch new/updated reservations from MySQL where updated_at > watermark
    3. Upsert into dim_guest, dim_source
    4. Upsert into fact_reservation_daily (handling multi-day stays)
    5. Update etl_watermark
    """
    logging.info("Syncing reservations incrementally")
    pass

default_args = {
    'owner': 'fandaqah_dw',
    'start_date': datetime(2026, 4, 1),
    'retries': 3,
    'retry_delay': timedelta(minutes=2),
}

with DAG(
    'realtime_reservations',
    default_args=default_args,
    description='Incremental sync of reservations to DW',
    schedule_interval='*/15 * * * *', # Every 15 minutes
    catchup=False
) as dag:

    sync_task = PythonOperator(
        task_id='sync_reservations',
        python_callable=sync_reservations,
    )
