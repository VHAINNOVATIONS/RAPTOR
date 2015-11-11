using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class TextReport : Report
    {
        string text;

        public TextReport() { }

        public string Text
        {
            get { return text; }
            set { text = value; }
        }
    }
}
