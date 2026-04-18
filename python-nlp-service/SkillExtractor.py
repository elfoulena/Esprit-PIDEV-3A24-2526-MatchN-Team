import re
import mysql.connector

def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="rh_tesst"
    )

def load_skills_from_db():
    skills = []
    cnx = get_connection()
    cursor = cnx.cursor()
    cursor.execute("select nom from competence_f")
    res=cursor.fetchall()
    for nom in res:
        skill = nom[0]
        skills.append(skill.lower())
    cursor.close()
    cnx.close()
    return skills

def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)   # supprimer la ponctuation
    text = re.sub(r'\s+', ' ', text).strip() # supprimer les espaces multiples
    return text

def extract_skills(desc, skills_db):
    desc_clean = clean_text(desc)
    found_skills = set()

    for skill in skills_db:
        pattern = r'\b' + re.escape(skill) + r'\b'

        if re.search(pattern, desc_clean, re.IGNORECASE):
            found_skills.add(to_title_case(skill))
    return list(found_skills)

def to_title_case(text: str):
    return text.title()

