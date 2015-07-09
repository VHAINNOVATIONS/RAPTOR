using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class QuestionnaireQuestion
    {
        string number;
        string text;
        string val;
        string branchFrom;
        string ifCondition;
        List<KeyValuePair<string, string>> choices;

        public string Number
        {
            get{return number;}
            set{number=value;}
        }

        public string Text
        {
            get{return text;}
            set{text=value;}
        }

        public string Value
        {
            get{return val;}
            set{val = value;}
        }

        public string BranchFromQuestion
        {
            get { return branchFrom; }
            set { branchFrom = value; }
        }

        public string BranchCondition
        {
            get { return ifCondition; }
            set { ifCondition = value; }
        }

        public List<KeyValuePair<string, string>> Choices
        {
            get { return choices; }
            set { choices = value; }
        }
    }
}
