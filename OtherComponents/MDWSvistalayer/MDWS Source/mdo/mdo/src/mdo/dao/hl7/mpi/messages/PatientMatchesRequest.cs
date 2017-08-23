using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao.hl7.segments;

namespace gov.va.medora.mdo.dao.hl7.mpi.messages
{
    public class PatientMatchesRequest
    {
        EncodingCharacters encChars;
        MshSegment msh;
        VtqSegment vtq;
        RdfSegment rdf;

        public PatientMatchesRequest() 
        {
            MSH = new MshSegment();
            VTQ = new VtqSegment();
            RDF = new RdfSegment();
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

        public MshSegment MSH
        {
            get { return msh; }
            set { msh = value; }
        }

        public VtqSegment VTQ
        {
            get { return vtq; }
            set { vtq = value; }
        }

        public RdfSegment RDF
        {
            get { return rdf; }
            set { rdf = value; }
        }

        public string toMessage()
        {
            string result = MSH.toSegment() + VTQ.toSegment() + RDF.toSegment();
            return result.Substring(0, result.Length - 1);  //Peel off last \r
        }
    }
}
