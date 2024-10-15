import sqlite3
from datetime import datetime


class CaffeineDB:
    def __init__(self):
        # Connect to the SQLite database
        self.conn = sqlite3.connect('caffeine.db')
        self.create_tables()

    def create_tables(self):
        # Create the table for storing caffeine logs if it doesn't exist
        cursor = self.conn.cursor()
        cursor.execute('''CREATE TABLE IF NOT EXISTS caffeine_log (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            amount REAL NOT NULL,
                            timestamp TEXT NOT NULL
                          )''')
        self.conn.commit()

    def log_caffeine(self, caffeine_amount):
        # Insert the amount of caffeine and the timestamp into the database
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cursor = self.conn.cursor()
        cursor.execute("INSERT INTO caffeine_log (amount, timestamp) VALUES (?, ?)", (caffeine_amount, timestamp))
        self.conn.commit()

    def get_total_caffeine_today(self):
        # Calculate total caffeine intake for the current day
        today = datetime.now().strftime("%Y-%m-%d")
        cursor = self.conn.cursor()
        cursor.execute("SELECT SUM(amount) FROM caffeine_log WHERE timestamp LIKE ?", (f'{today}%',))
        total_caffeine = cursor.fetchone()[0]

        if total_caffeine is None:
            total_caffeine = 0

        return total_caffeine
