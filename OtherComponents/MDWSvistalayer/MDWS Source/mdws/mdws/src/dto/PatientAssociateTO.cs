using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientAssociateTO : PersonTO
    {
        public string association;
        public string relationshipToPatient;
        public string facilityName;

        public PatientAssociateTO() { }

        public PatientAssociateTO(PatientAssociate mdo)
        {
            this.name = mdo.Name.getLastNameFirst();
            if (mdo.HomeAddress != null)
            {
                this.homeAddress = new AddressTO(mdo.HomeAddress);
            }
            if (mdo.HomePhone != null)
            {
                this.homePhone = new PhoneNumTO(mdo.HomePhone);
            }
            if (mdo.CellPhone != null)
            {
                this.cellPhone = new PhoneNumTO(mdo.CellPhone);
            }
            this.association = mdo.Association;
            this.relationshipToPatient = mdo.RelationshipToPatient;
            this.facilityName = mdo.FacilityName;
        }
    }
}
