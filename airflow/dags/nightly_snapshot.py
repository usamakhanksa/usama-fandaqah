from airflow import DAG
from airflow.operators.python import PythonOperator
from datetime import datetime, timedelta
import logging

# Simulation of ETL functions
def run_nightly_snapshot(**kwargs):
    """
    1. Read Nightly Snapshot Queue from MySQL (where status=pending)
    2. Aggregate occupancy, revenue, and stats for the business date
    3. Upsert into dim_hotel, dim_room, dim_room_type
    4. Upsert into fact_daily_occupancy, fact_reservation_daily
    5. Update MySQL: night_audit_log.dw_synced_at = NOW()
    6. Update MySQL: night_audit_snapshot_queue.status = 'done'
    """
    business_date = kwargs.get('execution_date').strftime('%Y-%m-%d')
    logging.info(f"Starting nightly snapshot ETL for {business_date}")
    # Logic implementation would go here (using SQLAlchemy or similar)
    pass

default_args = {
    'owner': 'fandaqah_dw',
    'depends_on_past': False,
    'start_date': datetime(2026, 4, 1),
    'email_on_failure': True,
    'retries': 2,
    'retry_delay': timedelta(minutes=5),
}

with DAG(
    'nightly_snapshot',
    default_args=default_args,
    description='Aggregates nightly occupancy and revenue stats into DW',
    schedule_interval='@daily',
    catchup=True,
    max_active_runs=1
) as dag:

    etl_task = PythonOperator(
        task_id='run_nightly_snapshot',
        python_callable=run_nightly_snapshot,
        provide_context=True,
    )
