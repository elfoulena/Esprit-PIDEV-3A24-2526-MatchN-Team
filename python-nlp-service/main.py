from fastapi import FastAPI
from SkillExtractor import extract_skills, load_skills_from_db

app = FastAPI()

@app.post("/extract-skills")
def extract(data: dict):
    description = data["description"]

    skills_db = load_skills_from_db()
    skills = extract_skills(description, skills_db)

    return {"skills": skills}