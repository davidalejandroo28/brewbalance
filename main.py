from kivy.app import App
from kivy.uix.boxlayout import BoxLayout
from kivy.uix.label import Label
from kivy.uix.button import Button
from kivy.uix.textinput import TextInput
from kivy.uix.popup import Popup
from kivy.uix.label import Label
import sqlite3
from datetime import datetime
from kivymd.uix.pickers import MDDatePicker


class CaffeineTrackerApp(App):

    def build(self):
        # Set up the main layout
        self.main_layout = BoxLayout(orientation="vertical", padding=10)

        # Add a Label
        self.main_layout.add_widget(Label(text="Caffeine Tracker"))

        # TextInput for entering the amount of caffeine
        self.caffeine_input = TextInput(hint_text="Enter amount of caffeine (mg)", multiline=False)
        self.main_layout.add_widget(self.caffeine_input)

        # Button to log the caffeine intake
        self.log_button = Button(text="Log Intake")
        self.log_button.bind(on_press=self.log_caffeine_intake)
        self.main_layout.add_widget(self.log_button)

        # Display the total caffeine for the day
        self.result_label = Label(text="Total Caffeine Today: 0 mg")
        self.main_layout.add_widget(self.result_label)

        # Set up the SQLite database
        self.conn = sqlite3.connect('caffeine.db')
        self.create_tables()

        # Update the daily intake
        self.update_daily_caffeine()

        return self.main_layout

    def create_tables(self):
        cursor = self.conn.cursor()
        cursor.execute('''CREATE TABLE IF NOT EXISTS caffeine_log (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            amount REAL NOT NULL,
                            timestamp TEXT NOT NULL
                          )''')
        self.conn.commit()

    def log_caffeine_intake(self, instance):
        # Get the caffeine amount from input
        caffeine_amount = float(self.caffeine_input.text)

        # Insert caffeine intake into the SQLite database
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cursor = self.conn.cursor()
        cursor.execute("INSERT INTO caffeine_log (amount, timestamp) VALUES (?, ?)", (caffeine_amount, timestamp))
        self.conn.commit()

        # Clear the input
        self.caffeine_input.text = ''

        # Update daily caffeine intake
        self.update_daily_caffeine()

    def update_daily_caffeine(self):
        # Calculate total caffeine for today
        today = datetime.now().strftime("%Y-%m-%d")
        cursor = self.conn.cursor()
        cursor.execute("SELECT SUM(amount) FROM caffeine_log WHERE timestamp LIKE ?", (f'{today}%',))
        total_caffeine = cursor.fetchone()[0]

        if total_caffeine is None:
            total_caffeine = 0

        # Update the label with the daily total
        self.result_label.text = f"Total Caffeine Today: {total_caffeine:.1f} mg"

        if total_caffeine > 400:
            self.show_limit_warning()

    def show_limit_warning(self):
        # Create a pop-up warning
        warning_popup = Popup(
            title="Caffeine Limit Exceeded!",
            content=Label(text="Warning: You've exceeded your daily caffeine limit of 400 mg!"),
            size_hint=(0.75, 0.5)
        )
        warning_popup.open()

    def open_date_picker(self):
        date_dialog = MDDatePicker(callback=self.on_date_selected)
        date_dialog.open()

    def on_date_selected(self, date_obj):
        selected_date = date_obj.strftime('%Y-%m-%d')

        # Fetch caffeine for the selected date
        total_caffeine = self.db.get_caffeine_by_date(selected_date)

        # Update the label to show data for the selected date
        self.root.ids.result_label.text = f"Caffeine on {selected_date}: {total_caffeine:.1f} mg"

if __name__ == '__main__':
    CaffeineTrackerApp().run()
