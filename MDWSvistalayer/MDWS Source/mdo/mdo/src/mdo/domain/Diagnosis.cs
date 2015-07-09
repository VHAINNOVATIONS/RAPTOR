using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Diagnosis
    {
        string icd9;
        string text;
        bool primary;

        public Diagnosis() { }

        public Diagnosis(string icd0, string text, bool primary)
        {
            Icd9 = icd9;
            Text = text;
            Primary = primary;
        }

        public string Icd9
        {
            get { return icd9; }
            set { icd9 = value; }
        }

        public string Text
        {
            get { return text; }
            set { text = value; }
        }

        public bool Primary
        {
            get { return primary; }
            set { primary = value; }
        }
    }
}
