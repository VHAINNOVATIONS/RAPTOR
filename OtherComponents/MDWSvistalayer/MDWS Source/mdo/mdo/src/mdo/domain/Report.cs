using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Report
    {
        string caseNumber;
        string id;
        string title;
        string timestamp;
        Author author;
        SiteId facility;
        string _type;
        string _text;

        public Report() { }

        public string Text
        {
            get { return _text; }
            set { _text = value; }
        }

        public string Type
        {
            get { return _type; }
            set { _type = value; }
        }

        public string CaseNumber
        {
            get { return caseNumber; }
            set { caseNumber = value; }
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

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public Author Author
        {
            get { return author; }
            set { author = value; }
        }

        public SiteId Facility
        {
            get { return facility; }
            set { facility = value; }
        }
    }
}
