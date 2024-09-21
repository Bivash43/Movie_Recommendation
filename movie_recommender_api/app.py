from flask import Flask, request, jsonify
import MySQLdb
import numpy as np
from scipy.sparse import coo_matrix
import implicit
from threadpoolctl import threadpool_limits
import pickle
import os

app = Flask(__name__)

# In-memory cache for user-item matrix and ALS model
cached_matrix = None
cached_model = None

# Path to save the trained model
MODEL_FILE = 'als_model.pkl'

# MySQL database connection
def get_db_connection():
    return MySQLdb.connect(
        host="127.0.0.1",  # Replace with your MySQL host
        db="movie_recommendation",  # Replace with your database name
        user="root",  # Replace with your MySQL username
        password="root"  # Replace with your MySQL password
    )

# Helper function to load user-item matrix from MySQL using sparse representation
def load_user_item_matrix():
    global cached_matrix

    if cached_matrix is not None:
        # If matrix is already cached, use it
        print("Using cached user-item matrix.")
        return cached_matrix

    conn = get_db_connection()
    cursor = conn.cursor()

    # Fetch ratings from the ratings table
    cursor.execute("SELECT user_id, movie_id, rating FROM ratings")
    ratings = cursor.fetchall()

    cursor.execute("SELECT MAX(user_id), MAX(movie_id) FROM ratings")
    max_user_id, max_movie_id = cursor.fetchone()

    # Create separate arrays for row (user_id), column (movie_id), and data (rating)
    rows, cols, data = [], [], []
    for rating in ratings:
        user_id, movie_id, rating_value = rating
        rows.append(user_id)
        cols.append(movie_id)
        data.append(rating_value)

    # Build a sparse matrix using coo_matrix
    user_item_matrix = coo_matrix((data, (rows, cols)), shape=(max_user_id + 1, max_movie_id + 1))

    cursor.close()
    conn.close()

    # Cache the matrix in memory
    cached_matrix = user_item_matrix

    return user_item_matrix

# Function to perform ALS and get movie recommendations
def recommend_movies(movie_id, k=10):
    global cached_model

    # Load user-item matrix
    user_item_matrix = load_user_item_matrix()

    # Convert the matrix to a format optimized for the implicit library (item-user matrix)
    item_user_data = user_item_matrix.T.tocsr()

    # Check if the ALS model is already loaded in memory
    if cached_model is None:
        # Try to load the model from the pickle file
        if os.path.exists(MODEL_FILE):
            print("Loading model from pickle file...")
            with open(MODEL_FILE, 'rb') as f:
                cached_model = pickle.load(f)
        else:
            # If the pickle file doesn't exist, train the model
            print("Training the ALS model...")

            # Initialize the ALS model
            model = implicit.als.AlternatingLeastSquares(factors=50, regularization=0.1, iterations=30)

            # Limit OpenBLAS to 1 thread for optimal performance
            with threadpool_limits(1, "blas"):
                # Fit the model on the item-user data
                model.fit(item_user_data)

            # Save the model to the pickle file
            with open(MODEL_FILE, 'wb') as f:
                pickle.dump(model, f)

            # Cache the model in memory
            cached_model = model
    else:
        print("Using cached ALS model.")

    # Get the movie's latent factors and recommend similar movies
    recommended_movies = cached_model.similar_items(movie_id, N=k + 1)

    # Debugging: Print the raw recommendations for the given movie
    print(f"Raw recommendations for Movie {movie_id}: {recommended_movies}")

    # Ensure proper filtering of the original movie
    recommended_movie_ids = [int(movie[0]) for movie in recommended_movies if int(movie[0]) != movie_id and int(movie[0]) != 1]

    # Debugging: Print the filtered movie IDs
    print(f"Filtered recommended movie IDs: {recommended_movie_ids[:k]}")

    return recommended_movie_ids[:k]

# API route for getting recommended movies
@app.route('/recommend', methods=['GET'])
def recommend():
    try:
        # Get the movie ID from query parameters
        movie_id = int(request.args.get('movie_id'))

        # Get recommended movies
        recommended_movies = recommend_movies(movie_id)

        # Return the recommended movie IDs as JSON
        return jsonify(recommended_movies)

    except Exception as e:
        # Return error message if anything goes wrong
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    # Run the Flask app
    app.run(host="0.0.0.0", port=5000)
