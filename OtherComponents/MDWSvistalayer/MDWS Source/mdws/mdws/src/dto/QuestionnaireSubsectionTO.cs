using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireSubsectionTO : AbstractTO
    {
        public string name;
        public string title;
        public QuestionArray questions;

        public QuestionnaireSubsectionTO() { }

        public QuestionnaireSubsectionTO(QuestionnaireSubsection mdo)
        {
            this.name = mdo.Name;
            this.title = mdo.Title;
            this.questions = new QuestionArray(mdo.Questions);
        }
    }
}
