using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderResponse
    {
        string name;
        string promptIen;
        int instance;
        string promptId;
        string ivalue;
        string evalue;
        string tvalue;
        string index;

        public OrderResponse() { }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string PromptIen
        {
            get { return promptIen; }
            set { promptIen = value; }
        }

        public int Instance
        {
            get { return instance; }
            set { instance = value; }
        }

        public string PromptId
        {
            get { return promptId; }
            set { promptId = value; }
        }

        public string Ivalue
        {
            get { return ivalue; }
            set { ivalue = value; }
        }

        public string Evalue
        {
            get { return evalue; }
            set { evalue = value; }
        }

        public string Tvalue
        {
            get { return tvalue; }
            set { tvalue = value; }
        }

        public string Index
        {
            get { return index; }
            set { index = value; }
        }
    }
}
