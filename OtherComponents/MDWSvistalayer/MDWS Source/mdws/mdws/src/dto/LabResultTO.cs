using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;
namespace gov.va.medora.mdws.dto
{
    public class LabResultTO : AbstractTO
    {
        public string timestamp;
        public LabTestTO test;
        public string specimenType;
        public string comment;
        public string value;
        public string boundaryStatus;
        public string labSiteId;

        public LabResultTO() { }

        public LabResultTO(LabResult mdo)
        {
            if (mdo.Test != null)
            {
                this.test = new LabTestTO(mdo.Test);
            }
            this.specimenType = mdo.SpecimenType;
            this.comment = mdo.Comment;
            //this.value = mdo.Value;
            this.value = StringUtils.stripInvalidXmlCharacters(mdo.Value); // http://trac.medora.va.gov/web/ticket/1447 - stripping at VistaConnection.query
            this.boundaryStatus = mdo.BoundaryStatus;
            this.labSiteId = mdo.LabSiteId;
            this.timestamp = mdo.Timestamp;
        }

        public LabResultTO(string specimenType, string comment, string value, string boundaryStatus, string labSiteId, string timestamp)
        {
            this.specimenType = specimenType;
            this.comment = comment;
            //this.value = value; 
            this.value = StringUtils.stripInvalidXmlCharacters(value); // http://trac.medora.va.gov/web/ticket/1447  // - stripping at VistaConnection.query
            this.boundaryStatus = boundaryStatus;
            this.labSiteId = labSiteId;
            this.timestamp = timestamp;
        }
    }
}
