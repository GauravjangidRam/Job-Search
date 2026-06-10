from flask import Flask, request, jsonify
try:
    from gpt4all import GPT4All
except Exception:
    GPT4All = None

app = Flask(__name__)

MODEL_NAME = 'gpt4all-lora-quantized.bin'  # adjust to your downloaded model filename
model = None
if GPT4All is not None:
    try:
        model = GPT4All(model_name=MODEL_NAME)
    except Exception as e:
        print('Could not load model:', e)

@app.route('/analyze', methods=['POST'])
def analyze():
    data = request.get_json(force=True)
    text = data.get('text', '')
    prompt = f"Analyze this resume for ATS keywords, missing items, and actionable suggestions. Return JSON with keys: score (0-100), suggestions (list), missing_sections (list).\n\nResume:\n{text}\n\nRespond succinctly as JSON." 

    if model is None:
        # fallback: simple heuristic
        suggestions = []
        missing = []
        score = 50
        if 'experience' not in text.lower():
            missing.append('Experience section')
            score -= 10
        if 'education' not in text.lower():
            missing.append('Education section')
            score -= 10
        if 'skills' not in text.lower():
            missing.append('Skills section')
            score -= 5
        suggestions.append('Consider adding a short professional summary at top.')
        return jsonify({'score': max(0, score), 'suggestions': suggestions, 'missing_sections': missing})

    resp = model.generate(prompt)
    # naive: return raw text under 'raw' key — improve parsing as needed
    return jsonify({'raw': resp})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)
