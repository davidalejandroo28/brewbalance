from kivy.app import App
from kivy.uix.boxlayout import BoxLayout
from kivy.uix.label import Label
from kivy.uix.button import Button
from kivy.uix.textinput import TextInput
from kivy.uix.popup import Popup
from kivymd.uix.pickers import MDDatePicker
import sqlite3
from datetime import datetime
from backend import CaffeineDB


class LoginScreen(BoxLayout):
    def __init__(self, app=None, **kwargs):
        super().__init__(**kwargs)
        self.app = app  # Reference to the main app to switch screens
        self.orientation = "vertical"

        # Username input
        self.add_widget(Label(text="Username"))
        self.username_input = TextInput(multiline=False)
        self.add_widget(self.username_input)

        # Password input
        self.add_widget(Label(text="Password"))
        self.password_input = TextInput(password=True, multiline=False)
        self.add_widget(self.password_input)

        # Login button
        self.login_button = Button(text="Login")
        self.login_button.bind(on_press=self.verify_login)
        self.add_widget(self.login_button)

    def verify_login(self, instance):
        username = self.username_input.text
        password = self.password_input.text

        # Hardcoded credentials check
        if username == "user" and password == "pass":
            self.app.switch_to_main_screen()  # Switch to the main caffeine tracker
        else:
            self.show_error_popup()

    def show_error_popup(self):
        popup = Popup(title="Login Failed",
                      content=Label(text="Invalid username or password"),
                      size_hint=(0.75, 0.5))
        popup.open()


class CaffeineTrackerApp(App):
    def build(self):
        # Initialize SQLite database
        self.db = CaffeineDB()

        # Start with the login screen
        self.main_layout = BoxLayout(orientation="vertical")
        self.switch_to_login_screen()
        return self.main_layout

    def switch_to_login_screen(self):
        # Clear the main layout and show the login screen
        self.main_layout.clear_widgets()
        self.main_layout.add_widget(LoginScreen(self))

    def switch_to_main_screen(self):
        # Clear the main layout and switch to the caffeine tracker screen
        self.main_layout.clear_widgets()

        # Main tracker layout
        self.main_layout.add_widget(Label(text="Caffeine Tracker"))

        # TextInput for caffeine intake
        self.caffeine_input = TextInput(hint_text="Enter amount of caffeine (mg)", multiline=False)
        self.main_layout.add_widget(self.caffeine_input)

        # Log intake button
        self.log_button = Button(text="Log Intake")
        self.log_button.bind(on_press=self.log_caffeine_intake)
        self.main_layout.add_widget(self.log_button)

        # Label to show total caffeine today
        self.result_label = Label(text="Total Caffeine Today: 0 mg")
        self.main_layout.add_widget(self.result_label)

        # Update daily caffeine intake
        self.update_daily_caffeine()

    def log_caffeine_intake(self, instance):
        caffeine_amount = float(self.caffeine_input.text)
        self.db.log_caffeine(caffeine_amount)

        # Clear input
        self.caffeine_input.text = ''

        # Update the caffeine total
        self.update_daily_caffeine()

    def update_daily_caffeine(self):
        total_caffeine = self.db.get_total_caffeine_today()
        self.result_label.text = f"Total Caffeine Today: {total_caffeine:.1f} mg"

        if total_caffeine > 400:
            self.show_limit_warning()

    def show_limit_warning(self):
        popup = Popup(title="Caffeine Limit Exceeded",
                      content=Label(text="Warning: You've exceeded your daily caffeine limit of 400 mg!"),
                      size_hint=(0.75, 0.5))
        popup.open()

    def open_date_picker(self):
        date_dialog = MDDatePicker(callback=self.on_date_selected)
        date_dialog.open()

    def on_date_selected(self, date_obj):
        selected_date = date_obj.strftime('%Y-%m-%d')
        total_caffeine = self.db.get_caffeine_by_date(selected_date)
        self.result_label.text = f"Caffeine on {selected_date}: {total_caffeine:.1f} mg"


if __name__ == '__main__':
    CaffeineTrackerApp().run()
