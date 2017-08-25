using System;
using System.Collections.Generic;
using gov.va.medora.mdo.dao.xml.questionnaire;

namespace gov.va.medora.mdo
{
    public class QuestionnaireSet
    {
        string name;
        string title;
        string description;
        List<QuestionnaireQuestion> questions;
        List<Questionnaire> questionnaires;

        public QuestionnaireSet() { }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string Title
        {
            get { return title; }
            set { title = value; }
        }

        public string Description
        {
            get { return description; }
            set { description = value; }
        }

        public List<QuestionnaireQuestion> Questions
        {
            get { return questions; }
            set { questions = value; }
        }

        public List<Questionnaire> Questionnaires
        {
            get { return questionnaires; }
            set { questionnaires = value; }
        }

        public static QuestionnaireSet getSet(string name)
        {
            QuestionnaireDao dao = new QuestionnaireDao();
            return dao.getSet(name);
        }
    }
}
