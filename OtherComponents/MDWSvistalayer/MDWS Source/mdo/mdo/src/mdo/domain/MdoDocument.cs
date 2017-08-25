using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    /// <summary>Generic document, including an id, title, and text.
    /// </summary>
    public class MdoDocument
    {
        string id;
        string title;
        string text;

        public MdoDocument() { }

        public MdoDocument(string id, string title)
        {
            Id = id;
            Title = title;
            Text = "";
        }

        public MdoDocument(string id, string title, string text)
        {
            Id = id;
            Title = title;
            Text = text;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Title
        {
            get { return title; }
            set { title = value; }
        }

        public string Text
        {
            get { return text; }
            set { text = value; }
        }

    }
}
