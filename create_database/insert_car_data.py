import os
import json
import mysql.connector
import re  # To use regular expressions for extracting numbers

# Database connection
db = mysql.connector.connect(
    host="localhost",  # Update with your database host
    user="root",       # Update with your MySQL username
    password="",       # Update with your MySQL password
    database="car_database"  # Update with your database name
)

cursor = db.cursor()

# Correct the base directory path to point to car_scraper/car_data
base_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), '../car_scraper/car_data')

def extract_seating_capacity(body_type):
    # Use regular expressions to find numeric values from the body_type string
    match = re.search(r'\d+', body_type)
    if match:
        return int(match.group(0))
    return 0  # Return 0 if no number is found

def extract_horsepower(horsepower_str):
    # Use regex to extract the numeric part of horsepower before "hp"
    match = re.match(r"(\d+)", horsepower_str)
    if match:
        return int(match.group(1))
    return None  # Return None if horsepower data is unavailable or malformed

def process_car_data(data, file_path):
    # Extract data for Cars table
    brand = data.get("brand", "Unknown")
    model = data.get("model_name", "Unknown")
    year = data.get("year", 0)
    price_range = data.get("price_range", "0 - 0")
    variants = data.get("variants", [])
    
    if not isinstance(variants, list):
        print(f"Error: 'variants' is not a list in file {file_path}. Skipping...")
        return

    # Compute average price from the price_range
    try:
        prices = [float(v["price"]) for v in variants if v["price"].replace('.', '', 1).isdigit()]
        average_price = sum(prices) / len(prices) if prices else 0.0
    except Exception as e:
        print(f"Error processing price data in file {file_path}: {e}")
        average_price = 0.0
    
    # Construct engine type (take the first variant as representative)
    engine_type = f"{variants[0]['engine_cc']} {variants[0]['fuel_type']}"
    
    # Insert into Performance, Safety, and Features tables for each variant
    for variant in variants:
        try:
            # Performance Table (extract horsepower from 'horsepower')
            horsepower_str = variant.get("horsepower", "0hp")
            horsepower = extract_horsepower(horsepower_str)
            cursor.execute(
                "INSERT INTO Performance (horsepower, torque, acceleration, top_speed) VALUES (%s, %s, %s, %s)",
                (horsepower, None, None, None)
            )
            performance_id = cursor.lastrowid

            # Features Table (extract seating_capacity from 'body_type')
            seating_capacity = extract_seating_capacity(variant["body_type"])
            cursor.execute(
                "INSERT INTO Features (seating_capacity, infotainment, upholstery, lighting) VALUES (%s, %s, %s, %s)",
                (seating_capacity, None, None, None)
            )
            features_id = cursor.lastrowid

            # Safety Table (no data available, insert placeholders)
            cursor.execute(
                "INSERT INTO Safety (crash_test_rating, airbag_count) VALUES (%s, %s)",
                (None, None)
            )
            safety_id = cursor.lastrowid

            # Now insert into Cars table using the foreign key references
            cursor.execute(
                "INSERT INTO Cars (make, model, year, price, engine_type, fuel_efficiency, performance_id, safety_id, features_id) "
                "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                (brand, model, year, average_price, engine_type, None, performance_id, safety_id, features_id)
            )
            car_id = cursor.lastrowid

        except Exception as e:
            print(f"Error processing variant {variant['trim_name']} in file {file_path}: {e}")
            continue

def process_json_file(file_path):
    with open(file_path, "r", encoding="utf-8") as f:
        data = json.load(f)
    
    # Check if data is a list or a dictionary
    if isinstance(data, list):
        # If the root element is a list, iterate through it
        for item in data:
            if isinstance(item, dict):
                process_car_data(item, file_path)
            else:
                print(f"Error: List element is not a dictionary in file {file_path}. Skipping...")
    elif isinstance(data, dict):
        # If the root element is a dictionary, process it directly
        process_car_data(data, file_path)
    else:
        print(f"Error: Unexpected data type {type(data)} in file {file_path}. Skipping...")

def process_all_files():
    # Loop through all brand folders and their json files
    for brand_folder in os.listdir(base_dir):
        brand_path = os.path.join(base_dir, brand_folder)
        if os.path.isdir(brand_path):
            for file_name in os.listdir(brand_path):
                if file_name.endswith(".json"):
                    file_path = os.path.join(brand_path, file_name)
                    print(f"Processing {file_path}...")
                    process_json_file(file_path)
    db.commit()

if __name__ == "__main__":
    try:
        process_all_files()
        print("All data inserted successfully!")
    except Exception as e:
        db.rollback()
        print("Error occurred:", e)
    finally:
        cursor.close()
        db.close()
