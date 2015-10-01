using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireSetTO : AbstractTO
    {
        public string name;
        public string title;
        public string description;
        public QuestionArray questions;
        public QuestionnaireArray questionnaires;

        public QuestionnaireSetTO() { }

        public QuestionnaireSetTO(QuestionnaireSet mdo)
        {
            this.name = mdo.Name;
            this.title = mdo.Title;
            this.description = mdo.Description;
            this.questions = new QuestionArray(mdo.Questions);
            this.questionnaires = new QuestionnaireArray(mdo.Questionnaires);
        }
    }
}
