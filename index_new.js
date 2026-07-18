import OpenAI from "openai";

const client = new OpenAI({
    apiKey: process.env.OPENAI_API_KEY,
    baseURL: process.env.OPENAI_BASE_URL
});

const response = await client.responses.create({
    model: "openai.gpt-oss-120b",
    input: "Can you analysis the my project of job search"
});

console.log(response.output_text);