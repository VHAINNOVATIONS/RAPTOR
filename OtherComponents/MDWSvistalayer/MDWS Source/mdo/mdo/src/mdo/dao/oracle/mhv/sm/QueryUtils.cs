using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public static class QueryUtils
    {
        internal static bool columnExists(string columnName, System.Data.IDataReader rdr)
        {
            if (String.IsNullOrEmpty(columnName) || rdr == null || rdr.FieldCount == 0)
            {
                return false;
            }
            for (int i = 0; i < rdr.FieldCount; i++)
            {
                if (String.Equals(rdr.GetName(i), columnName, StringComparison.CurrentCultureIgnoreCase))
                {
                    return true;
                }
            }
            return false;
        }

        internal static Dictionary<string, bool> getColumnExistsTable(IList<string> columnNames, System.Data.IDataReader rdr)
        {
            Dictionary<string, bool> columnExistenceTable = new Dictionary<string, bool>(columnNames.Count);
            foreach (string columnName in columnNames)
            {
                columnExistenceTable.Add(columnName, columnExists(columnName, rdr));
            }
            return columnExistenceTable;
        }
    }
}
