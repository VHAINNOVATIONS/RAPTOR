using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Questionnaire
    {
        string name;
        string user;
        string title;
        string description;
        List<QuestionnaireSection> sections;

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string User
        {
            get { return user; }
            set { user = value; }
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

        public List<QuestionnaireSection> Sections
        {
            get { return sections; }
            set { sections = value; }
        }
    }
}
