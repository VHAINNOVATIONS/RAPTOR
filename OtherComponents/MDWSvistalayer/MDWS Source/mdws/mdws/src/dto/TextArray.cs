using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdws.dto
{
    public class TextArray : AbstractArrayTO
    {
        public string[] text;

        public TextArray() { }

        public TextArray(string[] text)
        {
            if (text != null)
            {
                this.count = text.Length;
            }
            this.text = text;
        }

        public TextArray(IList<string> iList)
        {
            if (iList == null || iList.Count == 0)
            {
                return;
            }
            text = new string[iList.Count];
            iList.CopyTo(text, 0);
        }
    }
}
