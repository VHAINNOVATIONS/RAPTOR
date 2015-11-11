using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Observation
    {
        Author observer;
        Author recorder;
        string timestamp;
        SiteId facility;
        HospitalLocation location;
        ObservationType type;
        string comment;

        public Observation() { }

        public Author Observer
        {
            get { return observer; }
            set { observer = value; }
        }

        public Author Recorder
        {
            get { return recorder; }
            set { recorder = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public SiteId Facility
        {
            get { return facility; }
            set { facility = value; }
        }

        public HospitalLocation Location
        {
            get { return location; }
            set { location = value; }
        }

        public ObservationType Type
        {
            get { return type; }
            set { type = value; }
        }

        public string Comment
        {
            get { return comment; }
            set { comment = value; }
        }
    }
}
