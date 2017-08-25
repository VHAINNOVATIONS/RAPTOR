using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class QuestionnaireSubsection
    {
        string name;
        string title;
        List<QuestionnaireQuestion> questions;

        public QuestionnaireSubsection() { } 

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

        public List<QuestionnaireQuestion> Questions
        {
            get { return questions; }
            set { questions = value; }
        }
    }
}
