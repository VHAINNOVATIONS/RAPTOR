using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OEF_OIF
    {
        string location;
        DateTime fromDate;
        DateTime toDate;
        bool dataLocked;
        DateTime recordedDate;
        KeyValuePair<string, string> recordingSite;

        public OEF_OIF() { }

        public String Location
        {
            get { return location; }
            set { location = value; }
        }

        public DateTime FromDate
        {
            get { return fromDate; }
            set { fromDate = value; }
        }

        public DateTime ToDate
        {
            get { return toDate; }
            set { toDate = value; }
        }

        public bool DataLocked
        {
            get { return dataLocked; }
            set { dataLocked = value; }
        }

        public DateTime RecordedDate
        {
            get { return recordedDate; }
            set { recordedDate = value; }
        }

        public KeyValuePair<string, string> RecordingSite
        {
            get { return recordingSite; }
            set { recordingSite = value; }
        }
    }
}
