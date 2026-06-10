GPT4All local integration (no payment)

Overview
- GPT4All provides free, local models you can run without an account.
- This project extracts resume text and can POST it to a local analysis server.

Quick steps (Windows CPU):
1. Install Python 3.10+ and pip.
2. Create and activate a venv: python -m venv venv && venv\Scripts\activate
3. pip install gpt4all flask
4. Download a CPU-quantized model from https://gpt4all.io/ (e.g., gpt4all-lora-quantized.bin) and place it in the server folder.

Example minimal Flask server (save as server.py):

```python
from flask import Flask, request, jsonify
from gpt4all import GPT4All

app = Flask(__name__)
model = GPT4All(model_name='gpt4all-lora-quantized.bin')  # adjust filename

@app.route('/analyze', methods=['POST'])
def analyze():
    text = request.json.get('text','')
    prompt = f"Analyze this resume for ATS keywords, missing items, and suggestions:\n\n{text}\n\nProvide JSON with keys: score (0-100), suggestions (list), missing_sections (list)"
    resp = model.generate(prompt)
    # Very simple — adapt parsing as needed
    return jsonify({'raw': resp})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)
```

5. Run the server: python server.py
6. Upload a resume via the Laravel UI (/resume). The app will POST extracted text to http://127.0.0.1:5000/analyze and display the server response.

Notes
- CPU inference can be slow; pick a smaller, quantized model (3B/4B) for acceptable speed.
- No payment or API keys required.
- If desired, create a Windows service or background task to run the server.
