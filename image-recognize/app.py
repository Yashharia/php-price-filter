from flask import Flask, request
import cv2
import numpy as np
import os

app = Flask(__name__)

def find_matching_image(uploaded_image_path):
    images_directory = 'assets/images/'
    uploaded_image = cv2.imread(uploaded_image_path, cv2.IMREAD_GRAYSCALE)

    if uploaded_image is None:
        return "Invalid image format."

    best_score = float('inf')
    best_match = None

    for filename in os.listdir(images_directory):
        image_path = os.path.join(images_directory, filename)
        stored_image = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)
        if stored_image is None:
            continue

        # Calculate the difference score
        score = np.sum((uploaded_image - stored_image) ** 2)
        if score < best_score:
            best_score = score
            best_match = filename

    return best_match if best_match else "No matching image found."

@app.route('/recognize', methods=['GET'])
def recognize_image():
    image_path = request.args.get('image')
    match = find_matching_image(image_path)
    return match

if __name__ == '__main__':
    app.run(debug=True, port=5000)
