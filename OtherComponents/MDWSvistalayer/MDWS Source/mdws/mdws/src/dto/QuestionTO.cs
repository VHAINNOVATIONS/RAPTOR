using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionTO : AbstractTO
    {
        public string number;
        public string text;
        public string val;
        public string branchFromQuestion;
        public string branchCondition;
        public TaggedTextArray choices;

        public QuestionTO() { }

        public QuestionTO(QuestionnaireQuestion mdo)
        {
            this.number = mdo.Number;
            this.text = mdo.Text;
            this.val = mdo.Value;
            this.branchFromQuestion = mdo.BranchFromQuestion;
            this.branchCondition = mdo.BranchCondition;
            if (mdo.Choices != null)
            {
                this.choices = new TaggedTextArray();
                this.choices.results = new TaggedText[mdo.Choices.Count];
                for (int i = 0; i < mdo.Choices.Count; i++)
                {
                    KeyValuePair<string, string> kvp = mdo.Choices[i];
                    this.choices.results[i] = new TaggedText(kvp);
                }
                this.choices.count = mdo.Choices.Count;
            }
        
        }
    }
}
