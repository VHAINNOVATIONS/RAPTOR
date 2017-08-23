using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnairePageTO : AbstractTO
    {
        public string number;
        public QuestionnaireSubsectionArray sections;

        public QuestionnairePageTO() { }

        public QuestionnairePageTO(QuestionnairePage mdo)
        {
            this.number = mdo.Number;
            this.sections = new QuestionnaireSubsectionArray(mdo.Sections);
        }
    }
}
