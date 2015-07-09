using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class QuestionnaireSection
    {
        string name;
        string title;
        List<QuestionnairePage> pages;

        public QuestionnaireSection() { }

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

        public List<QuestionnairePage> Pages
        {
            get { return pages; }
            set { pages = value; }
        }
    }
}
