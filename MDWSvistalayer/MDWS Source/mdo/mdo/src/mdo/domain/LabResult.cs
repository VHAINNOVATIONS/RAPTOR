using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabResult
    {
        string _timestamp;
        LabTest test;
        string specimenType;
        string comment;
        string m_value;
        string boundaryStatus;
        string labSiteId;

        public LabResult() { }

        public string Timestamp
        {
            get { return _timestamp; }
            set { _timestamp = value; }
        }

        public LabTest Test
        {
            get { return test; }
            set { test = value; }
        }

        public string Value
        {
            get { return m_value; }
            set { m_value = value; }
        }

        public string Comment
        {
            get { return comment; }
            set { comment = value; }
        }

        public string BoundaryStatus
        {
            get { return boundaryStatus; }
            set { boundaryStatus = value; }
        }

        public string LabSiteId
        {
            get { return labSiteId; }
            set { labSiteId = value; }
        }

        public string SpecimenType
        {
            get { return specimenType; }
            set { specimenType = value; }
        }
    }
}
