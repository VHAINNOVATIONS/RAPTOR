using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Model.V24.Message;
using NHapi.Model.V24.Segment;
using NHapi.Base.Parser;

namespace gov.va.medora.mdo.dao.hl7.rxRefill
{
    public class QBP_Q13_PID : QBP_Q13
    {

        public QBP_Q13_PID() : base()
        {
            this.add(typeof(PID), true, false); 
        }

        public PID getPid()
        {
            return (PID)this.GetStructure("PID");
        }

        public string encode()
        {
            NHapi.Base.Parser.EncodingCharacters ec = new NHapi.Base.Parser.EncodingCharacters(HL7Constants.FIELD_SEPARATOR, HL7Constants.DEFAULT_DELIMITER);
            StringBuilder sb = new StringBuilder();
            
            sb.Append(PipeParser.Encode(this.MSH, ec));
            sb.Append(HL7Constants.SEGMENT_SEPARATOR);
            sb.Append(PipeParser.Encode(QPD, ec));
            sb.Append(HL7Constants.SEGMENT_SEPARATOR);
            sb.Append(PipeParser.Encode(getPid(), ec));
            sb.Append(HL7Constants.SEGMENT_SEPARATOR);
            sb.Append(PipeParser.Encode(RDF, ec));
            sb.Append(HL7Constants.SEGMENT_SEPARATOR);
            sb.Append(PipeParser.Encode(RCP, ec));

            return sb.ToString();
        }
    }
}
