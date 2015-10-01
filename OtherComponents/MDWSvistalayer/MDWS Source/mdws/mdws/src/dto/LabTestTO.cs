using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class LabTestTO : AbstractTO
    {
        public LabResultTO result;
        public LabSpecimenTO specimen;
        public string id;
        public string name;
        public string category;
        public string units;
        public string lowRef;
        public string hiRef;
        public string refRange;
        public string loinc;

        public LabTestTO() { }

        public LabTestTO(LabTest mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.units = mdo.Units;
            this.lowRef = mdo.LowRef;
            this.hiRef = mdo.HiRef;
            this.refRange = mdo.RefRange;
            this.loinc = mdo.Loinc;
            this.category = mdo.Category;

            if (mdo.Result != null)
            {
                result = new LabResultTO(mdo.Result.SpecimenType, mdo.Result.Comment, mdo.Result.Value, mdo.Result.BoundaryStatus, mdo.Result.LabSiteId, mdo.Result.Timestamp);
            }
            if (mdo.Specimen != null)
            {
                this.specimen = new LabSpecimenTO(mdo.Specimen);
            }
        }
    }
}
