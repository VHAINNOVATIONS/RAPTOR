using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class QuestionnairePage
    {
        string number;
        List<QuestionnaireSubsection> sections;

        public string Number
        {
            get { return number; }
            set { number = value; }
        }

        public List<QuestionnaireSubsection> Sections
        {
            get { return sections; }
            set { sections = value; }
        }
    }
}
