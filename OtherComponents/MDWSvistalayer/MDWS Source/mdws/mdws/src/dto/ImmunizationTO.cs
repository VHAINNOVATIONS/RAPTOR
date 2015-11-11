using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ImmunizationTO : AbstractTO
    {
        public UserTO administrator;
        public string administeredDate;
        public string anatomicSurface;
        public string comments;
        public string contraindicated;
        public CptCodeTO cptCode;
        public VisitTO encounter;
        public string id;
        public string lotNumber;
        public string manufacturer;
        public string name;
        public UserTO orderedBy;
        public string reaction;
        public string series;
        public string shortName;

        public ImmunizationTO() { /* parameterless constructor */ }

        public ImmunizationTO(Immunization immnunization)
        {
            if (immnunization == null)
            {
                return;
            }

            administrator = new UserTO(immnunization.Administrator);
            administeredDate = immnunization.AdministeredDate;
            anatomicSurface = immnunization.AnatomicSurface;
            comments = immnunization.Comments;
            contraindicated = immnunization.Contraindicated;
            cptCode = new CptCodeTO(immnunization.CptCode);
            encounter = new VisitTO(immnunization.Encounter);
            id = immnunization.Id;
            lotNumber = immnunization.LotNumber;
            manufacturer = immnunization.Manufacturer;
            name = immnunization.Name;
            orderedBy = new UserTO(immnunization.OrderedBy);
            reaction = immnunization.Reaction;
            series = immnunization.Series;
            shortName = immnunization.ShortName;
        }
    }
}