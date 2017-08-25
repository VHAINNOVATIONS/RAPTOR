using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireTO : AbstractTO
    {
        public string name;
        public string user;
        public string title;
        public string description;
        public QuestionnaireSectionArray sections;

        public QuestionnaireTO() { }

        public QuestionnaireTO(Questionnaire mdo)
        {
            this.name = mdo.Name;
            this.user = mdo.User;
            this.title = mdo.Title;
            this.description = mdo.Description;
            this.sections = new QuestionnaireSectionArray(mdo.Sections);
        }
    }
}
