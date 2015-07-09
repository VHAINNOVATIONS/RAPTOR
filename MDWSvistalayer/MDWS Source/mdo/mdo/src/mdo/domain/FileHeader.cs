using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class FileHeader
    {
        string name;
        string alternateName;
        string latestId;
        Int64 nrex;
        ArrayList characteristics;

        public FileHeader() { }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string AlternateName
        {
            get { return alternateName; }
            set { alternateName = value; }
        }

        public string LatestId
        {
            get { return latestId; }
            set { latestId = value; }
        }

        public long NumberOfRecords
        {
            get { return nrex; }
            set { nrex = value; }
        }

        public ArrayList Characteristics
        {
            get { return characteristics; }
            set { characteristics = value; }
        }
    }
}
