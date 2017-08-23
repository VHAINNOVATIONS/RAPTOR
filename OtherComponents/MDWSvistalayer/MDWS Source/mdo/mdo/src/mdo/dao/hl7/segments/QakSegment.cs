using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.hl7.segments
{
    public class QakSegment
    {
        EncodingCharacters encChars = new EncodingCharacters();
        string qryTag = "";
        string qryResponseStatus = "";

        public QakSegment() { }

        public QakSegment(string rawSegmentString) 
        {
		    parse(rawSegmentString);
	    }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public string QueryTag
        {
            get { return qryTag; }
            set { qryTag = value; }
        }

        public string QueryResponseStatus
        {
            get { return qryResponseStatus; }
            set { qryResponseStatus = value; }
        }

        public void parse(string rawSegmentString)
	    {
            string[] flds = StringUtils.split(rawSegmentString, EncodingChars.FieldSeparator);

            if (flds.Length < 3)
            {
                throw new Exception("Invalid QAK segment: less than 3 fields");
            }

            if (flds[0] != "QAK")
		    {
			    throw new Exception("Invalid QAK segment: incorrect header");
		    }
    		
		    QueryTag = flds[1];
		    QueryResponseStatus = flds[2].Trim();
	    }
	
    }
}
