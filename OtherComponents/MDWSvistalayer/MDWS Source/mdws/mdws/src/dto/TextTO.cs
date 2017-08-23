using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdws.dto
{
    public class TextTO : AbstractTO
    {
        public string text;

        public TextTO() { }

        public TextTO(String s)
        {
            text = s;
        }
    }
}
