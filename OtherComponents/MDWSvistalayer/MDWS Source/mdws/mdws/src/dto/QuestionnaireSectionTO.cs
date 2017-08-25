using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireSectionTO : AbstractTO
    {
        public string name;
        public string title;
        public QuestionnairePageArray pages;

        public QuestionnaireSectionTO() { }

        public QuestionnaireSectionTO(QuestionnaireSection mdo)
        {
            this.name = mdo.Name;
            this.title = mdo.Title;
            this.pages = new QuestionnairePageArray(mdo.Pages);
        }
    }
}
