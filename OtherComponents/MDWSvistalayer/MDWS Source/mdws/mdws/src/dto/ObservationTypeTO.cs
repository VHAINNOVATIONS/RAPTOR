using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

/// <summary>
/// Summary description for ObservationTypeTO
/// </summary>

namespace gov.va.medora.mdws.dto
{
    public class ObservationTypeTO : AbstractTO
    {
        public string id;
        public string category;
        public string name;
        public string shortName;
        public string dataId;
        public string dataName;
        public string dataType;

        public ObservationTypeTO() { }

        public ObservationTypeTO(ObservationType mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.category = mdo.Category;
            this.shortName = mdo.ShortName;
            this.dataId = mdo.DataId;
            this.dataName = mdo.DataName;
            this.dataType = mdo.DataType;
        }
    }
}
