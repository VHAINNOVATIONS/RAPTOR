using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class CrossRef
    {
        public string Name { get; set; }
        public string FieldNumber { get; set; }
        public string FieldName { get; set; }
        public VistaFile File { get; set; }
        public string DD { get; set; }

        public override string ToString()
        {
            return String.Format("Name: {0}, Field #: {1}, Field Name: {2}, DD: {3}", this.Name, this.FieldNumber, this.FieldName, this.DD);
        } 
    }
}
