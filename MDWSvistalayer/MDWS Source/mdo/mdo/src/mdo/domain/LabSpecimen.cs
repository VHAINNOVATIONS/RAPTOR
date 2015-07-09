using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabSpecimen
    {
        string id;
        string name;
        string collectionDate;
        string accessionNum;
        string site;
        SiteId facility;
        string reportDate;

        public LabSpecimen() { }

        public LabSpecimen(string id, string name, string collectionDate, string accessionNum)
        {
            Id = id;
            Name = name;
            CollectionDate = collectionDate;
            AccessionNumber = accessionNum;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string CollectionDate
        {
            get { return collectionDate; }
            set { collectionDate = value; }
        }

        public string AccessionNumber
        {
            get { return accessionNum; }
            set { accessionNum = value; }
        }

        public string Site
        {
            get { return site; }
            set { site = value; }
        }

        public SiteId Facility
        {
            get { return facility; }
            set { facility = value; }
        }

        public string ReportDate
        {
            get { return reportDate; }
            set { reportDate = value; }
        }
    }
}
